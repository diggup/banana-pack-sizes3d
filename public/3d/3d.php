<?PHP
	
	header("Content-Type: application/javascript");
	$banana_config = require('../../config/banana.php');
	rsort($banana_config['pack_sizes']);
	
?>

function dmloaded () {
	
	// world mesh files to load
	world.mesh_path = '3d/';
	world.model_files = [
		'bananas'
	];
	
	// number of number wheels
	world.wheels_pos = [];
	
	// pack sizes array
	world.packSizes = [<?PHP echo implode(',', $banana_config['pack_sizes']); ?>];
	
	// pack images array
	world.crateMats = [];
	
	var canvas = document.getElementById('renderCanvas');
	var engine = new BABYLON.Engine(canvas, true);
	
	
	// WINDOW RESIZE
	function resize_everything() {
		engine.resize();
	}
	window.addEventListener('resize', function(){
		resize_everything();
	});
	resize_everything();
	
	
	
	// MAIN CREATE SCENE FUNCTION
	var createScene = function() {
		
		
/****************************************************************************************************************************
	START FUNCTIONS
*****************************************************************************************************************************/
		
		// IMPORT MODELS
		function import_models () {
			var length = world.model_files.length;
			for (var i = 0; i < length; i++) {
				var modelFile = world.model_files[i] + '.babylon';
				BABYLON.SceneLoader.ImportMesh("", world.mesh_path, modelFile, scene, function (newMeshes, particleSystems, Skeletons) {
					newMeshes.forEach(function(mesh){
						BABYLON.Tags.EnableFor(mesh);
						mesh.addTags("inside");
					});
				});
			}
		}
		
		
		// CHECK VAR IS A NUMBER
		function IsNumeric(val) {
			return Number(parseInt(val)) === val;
		}
		
		
		// PAD STRING - RIGHT
		String.prototype.rpad = function(padString, length) {
			var str = this;
			while (str.length < length)
				str = str + padString;
			return str;
		}		
		
		
		// MOVE WHEELS - CALCULATE ROTATIONS
		world.moveWheels = function (digitsStr, packResults) {
			world.packResults = packResults;
			maxDigits = 6;
			digitsStr = digitsStr.rpad('0', maxDigits);
			for (var i = 0; i < maxDigits; i++) {
				currentDigit = Number(digitsStr[i]);
				wheelName = 'wheel.' + i;
				oldRotation = scene.getMeshByName(wheelName).rotation.x;
				if (currentDigit > -1 && currentDigit != world.wheels_pos[i]) {
					// calculate new rotation
					newRotation = ((10 - world.wheels_pos[i]) + currentDigit) * 36; // 360d/10=36
					newRotation = new BABYLON.Angle.FromDegrees((newRotation)).radians();
					newRotation = oldRotation - newRotation;
					// rotate the wheel
					wheelAnim(wheelName, oldRotation, newRotation);
					world.wheels_pos[i] = currentDigit;
				}
			}
			displayCrates();
		}
		
		// CREATE WHEEL ANIMATION
		var wheelAnim = function (wheelName, rotA, rotB) {
			fRate = 2;
			wRotate = new BABYLON.Animation("wRotate", "rotation", fRate, BABYLON.Animation.ANIMATIONTYPE_VECTOR3, BABYLON.Animation.ANIMATIONLOOPMODE_CONSTANT);
			wRotateKeyFrames = [];
			wheel = scene.getMeshByName(wheelName);
			meshRot = wheel.rotation;
			start_rot = new BABYLON.Vector3(rotA, meshRot.y, meshRot.z);
			end_rot = new BABYLON.Vector3(rotB, meshRot.y, meshRot.z);
			wRotateKeyFrames.push({ frame: 0, value: start_rot });
			wRotateKeyFrames.push({ frame: 2, value: end_rot });
			wRotate.setKeys(wRotateKeyFrames);
			scene.beginDirectAnimation(wheel, [wRotate], 0, 2, false);
		}
		
		// DISPLAY BANANA CRATES
		var displayCrates = function() {
			crateX = -3.3225;
			crateY = 1.5;
			crateZ = -2.3445;
			start_pos = new BABYLON.Vector3(0, 0, 60);
			fRate = 5;
			fDelay = 0;
			labelZincrease = 0;
			// remove old crates and text planes from scene
			for (var i = 0; i < world.packSizes.length; i++) {
				if (scene.getMeshByName('crate' + i) != null) {
					scene.getMeshByName('crate' + i).dispose();
				}
				if (scene.getMeshByName('txtPlane' + i) != null) {
					scene.getMeshByName('txtPlane' + i).dispose();
				}
			}
			// create and animate crates
			for (var i = 0; i < world.packResults.length; i++) {
				if (world.packResults[i].trim() != '') {
					res = world.packResults[i].split(':');
					packSize = res[0];
					packAmt = res[1];
					crateY -= 1;
					// clone crate
					crate_clone = scene.getMeshByName('crate').clone();
					crate_clone.name = 'crate' + i;
					crate_clone.material = world.crateMats[packSize];
					crate_clone.visibility = 1;
					crate_clone.position = start_pos;
					// animate crate from start_pos to end_pos
					end_pos = new BABYLON.Vector3(crateX, crateY, crateZ);
					fRate += fDelay;
					crateMove = new BABYLON.Animation("crateMove" + i, "position", fRate, BABYLON.Animation.ANIMATIONTYPE_VECTOR3, BABYLON.Animation.ANIMATIONLOOPMODE_CONSTANT);
					crateKFs = [];
					meshRot = wheel.rotation;
					var easingFunction = new BABYLON.SineEase();
					easingFunction.setEasingMode(BABYLON.EasingFunction.EASINGMODE_EASEINOUT);
					crateMove.setEasingFunction(easingFunction);
					crateKFs.push({ frame: 0, value: start_pos });
					crateKFs.push({ frame: fRate, value: end_pos });
					crateMove.setKeys(crateKFs);
					animatable = scene.beginDirectAnimation(crate_clone, [crateMove], 0, fRate, false);
					fDelay += 0.5;
					animatable.speedRatio += fDelay;
					// make labels
					labelZincrease += 0.05;
					if (scene.getMeshByName('txtPlane' + i) == null) {
						crateLabel(crateX + 2, crateY - 0.6, crateZ - labelZincrease, 'x ' + packAmt, 'txtPlane' + i);
					}
				}
			}
		}
		
		// CREATE CRATE LABELS
		var crateLabel = function(posX, posY, posZ, txt, txtName) {
			var outputplane = BABYLON.Mesh.CreatePlane(txtName, 2, scene, false);
			outputplane.material = new BABYLON.StandardMaterial("outputplane", scene);
			outputplane.position = new BABYLON.Vector3(posX, posY, posZ);
			var outputplaneTexture = new BABYLON.DynamicTexture("dynamic texture", 512, scene, true);
			outputplane.material.diffuseTexture = outputplaneTexture;
			outputplane.material.specularColor = new BABYLON.Color3(0, 0, 0);
			outputplane.material.emissiveColor = new BABYLON.Color3(1, 1, 1);
			outputplane.material.backFaceCulling = false;
			outputplaneTexture.drawText(txt, null, 140, "bold 140px verdana", "black", "white");
		}
		
		
/****************************************************************************************************************************
	END FUNCTIONS
*****************************************************************************************************************************/
		
		
		// SCENE
		var scene = new BABYLON.Scene(engine);
		scene.clearColor = new BABYLON.Color3(1, 1, 1); // background colour
		
		// IMPORT ALL MESHES
		import_models();
		
		// MAIN CAMERA
		var camera = new BABYLON.FreeCamera('camera', new BABYLON.Vector3(-11.0461,-1.3542, -11.7439), scene);
		camera.rotation = new BABYLON.Vector3(-0.0322, 0.7897, 0);
		camera.fov = 0.6;
		
		// LIGHTS
		var hemi = new BABYLON.HemisphericLight("Hemi0", new BABYLON.Vector3(0, 0, 0), scene);
		hemi.groundColor = new BABYLON.Color3(1, 1, 1);
		
		
		// PREVENT RIGHT-CLICK
		document.addEventListener("contextmenu", function (e) { e.preventDefault();	});
		
		
		/*******************************************
		 EXECUTE WHEN SCENE IS READY
		********************************************/
		scene.executeWhenReady(function () {
			
			scene.getMeshByName('crate').visibility = 0;
			
			// CREATE WHEELS
			world.wheels_pos[0] = 0;
			for (var i = 1; i < 6; i++) {
				tmp = scene.getMeshByName('wheel.0').clone();
				tmp.name = 'wheel.' + i;
				tmp.position.x += (i * 0.7);
				world.wheels_pos[i] = 0;
			}
			
			// CREATE CRATE MATERIALS
			for (var i = 0; i < world.packSizes.length; i++) {
				world.crateMats[world.packSizes[i]] = scene.getMeshByName('crate').material.clone(world.packSizes[i]);
				world.crateMats[world.packSizes[i]].diffuseTexture = new BABYLON.Texture("3d/crate_img.php?num=" + world.packSizes[i], scene);
			}
			
			// HIDE LOADING SCREEN
			//document.getElementById('loading').style.display = 'none';
			
			
			
		}); // end executeWhenReady
		
		return scene;
		
	}
	
	
	dmloaded.createScene = createScene;
	
	
	// MAIN
	var scene = createScene();
	engine.runRenderLoop(function(){
		
		scene.render();
		
	});
	
	
};

window.addEventListener('DOMContentLoaded', dmloaded);

